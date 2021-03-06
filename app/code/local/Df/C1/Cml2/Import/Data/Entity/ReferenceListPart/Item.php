<?php
namespace Df\C1\Cml2\Import\Data\Entity\ReferenceListPart;
class Item extends \Df\C1\Cml2\Import\Data\Entity {
	/**
	 * @override
	 * @return string
	 */
	public function getExternalId() {
		/** @var string $result */
		$result =
			$this->leaf(
				/**
				 * 1С:Управление торговлей 10.2 + дополнение от Битрикса:
				 *
					<Свойство>
						<Ид>b79b0fdd-c8a5-11e1-a928-4061868fc6eb</Ид>
						<Наименование>Производитель</Наименование>
						<ТипыЗначений>
							<ТипЗначений>
								<Тип>Справочник</Тип>
								<Описание>Значения свойств объектов</Описание>
								<ВариантыЗначений>
									<ВариантЗначения>
										<Ид>b79b0fde-c8a5-11e1-a928-4061868fc6eb</Ид>
										<Значение>Sony</Значение>
									</ВариантЗначения>
									<ВариантЗначения>
										<Ид>65fa6244-c8af-11e1-a928-4061868fc6eb</Ид>
										<Значение>Pentax</Значение>
									</ВариантЗначения>
								</ВариантыЗначений>
							</ТипЗначений>
						</ТипыЗначений>
					</Свойство>
				 */
				'Ид'
				/**
				 * 1С:Управление торговлей 11:
				 *
					<Свойство>
						<Ид>69a1a785-f26f-11e1-990a-000c292511ad</Ид>
						<Наименование>Разрешение</Наименование>
						<ТипЗначений>Справочник</ТипЗначений>
						<ВариантыЗначений>
							<Справочник>
								<ИдЗначения>69a1a786-f26f-11e1-990a-000c292511ad</ИдЗначения>
								<Значение>HD Ready</Значение>
							</Справочник>
							<Справочник>
								<ИдЗначения>69a1a787-f26f-11e1-990a-000c292511ad</ИдЗначения>
								<Значение>Full HD</Значение>
							</Справочник>
						</ВариантыЗначений>
					</Свойство>
				 */
				,$this->leaf('ИдЗначения')
			)
		;
		df_result_string_not_empty($result);
		return $result;
	}

	/**
	 * Иногда значение может отсутствовать.
	 * В реальном примере ниже из магазина dvplay.ru
	 * значение отсутствует у первого элемента справочника:
		<Свойство>
			<Ид>14196403-178e-11e3-a8aa-001517451451</Ид>
			<Наименование>Видео формат Xvid</Наименование>
			<ТипЗначений>Справочник</ТипЗначений>
			<ВариантыЗначений>
				<Справочник>
					<ИдЗначения>00000000-0000-0000-0000-000000000000</ИдЗначения>
				</Справочник>
				<Справочник>
					<ИдЗначения>14196404-178e-11e3-a8aa-001517451451</ИдЗначения>
					<Значение>-</Значение>
				</Справочник>
				<Справочник>
					<ИдЗначения>14196405-178e-11e3-a8aa-001517451451</ИдЗначения>
					<Значение>+</Значение>
				</Справочник>
			</ВариантыЗначений>
			<ДляТоваров>true</ДляТоваров>
		</Свойство>
	 * http://magento-forum.ru/topic/4040/
	 * @override
	 * @return string
	 */
	public function getName() {return df_nts($this->leaf('Значение'));}
}